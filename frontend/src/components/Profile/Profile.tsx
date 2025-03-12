import { useState } from 'react'
import Input from '../Input/Input'
// @ts-ignore
import classes from './Profile.module.css'

function Profile(props: any) {
	const [avatar, setAvatar] = useState<string | null>(null)

	const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
		const file = event.target.files?.[0]
		if (file) {
			const reader = new FileReader()
			reader.onload = e => {
				setAvatar(e.target?.result as string)
			}
			reader.readAsDataURL(file)
		}
	}

	return (
		<div className={classes.Profile}>
			<form onSubmit={props.onSubmitChanges}>
				<div className={classes.avatarUploader}>
					<label htmlFor='avatarInput' className={classes.avatarLabel}>
						<div
							style={{
								backgroundImage: `url(${
									avatar || 'http://localhost/uploads/avatars/1.jpg'
								})`,
							}}
							className={classes.avatar}
						/>
					</label>
					<input
						type='file'
						id='avatarInput'
						accept='image/*'
						className={classes.avatarInput}
						onChange={handleFileChange}
					/>
				</div>
				<p className={classes.Nickname}>{props.nickname}</p>
				<Input type='checkbox' title='Скрыть почту' name='hideMail' />
				<div className={classes.buttons}>
					<button onClick={props.onCancelChanges} className={classes.cancel}>
						Отменить
					</button>
					<Input type='submit' title='Подтвердить' name='submit' />
				</div>
			</form>
		</div>
	)
}

export default Profile
